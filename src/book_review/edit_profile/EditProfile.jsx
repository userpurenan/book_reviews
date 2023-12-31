import React, { useEffect, useState } from 'react';
import { useCookies } from 'react-cookie';
import { useForm } from 'react-hook-form';
import { useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import Compressor from 'compressorjs';
import { useUrl } from '../../useUrl';
import { Header } from '../header/Header';
import './EditProfile.scss';

//コンポーネント名は大文字始まりでOK
export const EditProfile = () => {
  const {
    register,
    handleSubmit,
    formState: { errors }
  } = useForm(); // バリデーションのフォームを定義。
  const [name, setName] = useState('');
  const [ImgFile, setImgFile] = useState(); //「ImgFile」にはリサイズした画像が入る
  const [imgUrl, setImgUrl] = useState(''); //画面に表示させる画像のurlをセット
  const [user, setUsers] = useState('');
  const edit_user_neme_url = useUrl('user_operation'); //カスタムフック。このコンポーネントで使うapiのurlが返る
  const get_user_url = useUrl('user_operation');
  const icon_upload_url = useUrl('icon_upload');
  const [cookies] = useCookies();
  const auth = useSelector((state) => state.auth.isSignIn);

  const [errorMessage, setErrorMessage] = useState('');
  const navigate = useNavigate();
  const handleNameChange = (e) => setName(e.target.value);

  /**
   * ユーザー更新時の画像をアイコンにするスタイルの調整はsassでやってもうまくいかなかったためjsx側でしました。
    （恐らく、動的にimgタグのsrcを変化させているため、sassでスタイルを調整すると二回目以降、画像をファイルから指定した際にスタイルが反映されなくなるからかと。）
   */
  const defaultStyle = {
    width: '50px',
    height: '50px',
    borderRadius: '50%'
  };

  //関数は小文字始まり
  const updateName = () => {
    const formdata = new FormData();
    formdata.append('icon', ImgFile, ImgFile.name);

    const headers = {
      authorization: `Bearer ${cookies.token}`
    };

    axios
      .patch(edit_user_neme_url, { name: name }, { headers })
      .then(async () => {
        await axios.post(icon_upload_url, formdata, {
          headers,
          'Content-Type': 'multipart/form-data'
        });
        navigate('/');
      })
      .catch((err) => {
        setErrorMessage(`更新に失敗しました。${err}`);
      });
  };

  //画像が1MBより大きかったらリサイズする関数
  const handleIconUrlChange = (e) => {
    const file = e.target.files[0];

    // 1MB以上の場合
    if (file.size > 1024 * 1024) {
      //画像のリサイズする関数
      new Compressor(file, {
        quality: 0.3,
        maxHeight: 10,
        maxWidth: 10,
        success(result) {
          const url = URL.createObjectURL(result);
          setImgUrl(url); // imgタグをusestateにセット「usestateにurlをセットする」
          setImgFile(result);
        },
        error(err) {
          console.error('画像の圧縮エラー:', err.message);
        }
      });
    } else {
      const url = URL.createObjectURL(file);
      setImgUrl(url); // imgタグをusestateにセット「usestateにurlをセットする」
      setImgFile(file);
    }
  };

  useEffect(() => {
    if (auth === false) return navigate('/login'); //「navegate」はレンダリング時に呼び出したらだめらしい。（「useEfect」内で呼び出そうとのエラーが出た）

    axios
      .get(get_user_url, {
        //アクセス時、ユーザーの情報を取得する
        headers: {
          authorization: `Bearer ${cookies.token}`
        }
      })
      .then((res) => {
        setUsers(res.data);
        setImgUrl(res.data.icon_url);
      });
  }, []);

  return (
    <div className="page">
      <Header />
      <main className="update">
        <div className="edit_user_float_page">
          <h2>ユーザー情報編集</h2>
          <p className="error-message">{errorMessage}</p>
          <form onSubmit={handleSubmit(updateName)} className="update__form">
            <label>ユーザー名</label>
            <br />
            <input
              type="text"
              {...register('name', { required: true })}
              onChange={handleNameChange}
              className="update__form--name"
              defaultValue={user.name} /*「value={user.name}」だとユーザーの名前を修正できないので「defaultValue」を使う*/
            />
            <p>{errors.name?.type === 'required' && <b className="error-message">※アカウント名を入力してください。</b>}</p>
            <label>アイコン画像アップロード</label>
            <br />
            <input type="file" onChange={handleIconUrlChange} accept=".jpg, .png" className="icon-uploads" />
            <div>
              <img src={imgUrl} id="icon" alt="ユーザーのアイコン画像" style={defaultStyle} />
            </div>
            <br />
            <button type="submit" className="update__form--button">
              更新
            </button>
          </form>
        </div>
      </main>
    </div>
  );
};
