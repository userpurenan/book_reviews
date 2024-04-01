import React, { useEffect, useState } from 'react';
import { useCookies } from 'react-cookie';
import { useForm } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import Compressor from 'compressorjs';
import defaultIcon from '../../defaultIcon.png';
import { useUrl } from '../../useUrl';
import Loading from '../Loading';
import { Header } from '../header/Header';
import './EditProfile.scss';

//コンポーネント名は大文字始まりでOK
export const EditProfile = () => {
  const [name, setName] = useState('');
  const [imageFile, setImageFile] = useState(null); //「ImgFile」にはリサイズした画像が入る
  const [IconUrl, setIconUrl] = useState(defaultIcon); //画面に表示させる画像のurlをセット
  const [userName, setUserName] = useState('');
  const editUserNameUrl = useUrl('userOperation'); //カスタムフック。このコンポーネントで使うapiのurlが返る
  const getUserUrl = useUrl('userOperation');
  const iconUploadUrl = useUrl('iconUpload');
  const [cookies] = useCookies();
  const {
    register,
    handleSubmit,
    formState: { errors }
  } = useForm(); // バリデーションのフォームを定義。

  const [errorMessage, setErrorMessage] = useState('');
  const navigate = useNavigate();
  const handleNameChange = (e) => setName(e.target.value);

  //関数は小文字始まり
  const updateName = () => {
    const headers = {
      authorization: `Bearer ${cookies.token}`
    };

    axios
      .patch(editUserNameUrl, { name: name }, { headers })
      .then(async () => {
        await iconImageUpdate();
        navigate('/');
      })
      .catch((err) => {
        setErrorMessage(`更新に失敗しました。${err}`);
      });
  };

  const iconImageUpdate = () => {
    if (imageFile) {
      const formdata = new FormData();
      formdata.append('icon', imageFile, imageFile.name);

      axios.post(iconUploadUrl, formdata, {
        headers: {
          authorization: `Bearer ${cookies.token}`,
          'content-Type': 'multipart/form-data'
        }
      });
    }
  };

  //画像が1MBより大きかったらリサイズする関数
  const handleIconUrlChange = (e) => {
    const file = e.target.files[0];
    const url = URL.createObjectURL(file);
    setIconUrl(url); // imgタグをusestateにセット「usestateにurlをセットする」

    // 1MB以上の場合
    if (file.size > 1024 * 1024) {
      //画像のリサイズする関数
      new Compressor(file, {
        quality: 0.3,
        maxHeight: 10,
        maxWidth: 10,
        success(result) {
          setImageFile(result);
        },
        error(err) {
          setErrorMessage(`画像の圧縮エラー:${err.message}`);
        }
      });
    } else {
      setImageFile(file);
    }
  };

  useEffect(() => {
    axios
      .get(getUserUrl, {
        //アクセス時、ユーザーの情報を取得する
        headers: {
          authorization: `Bearer ${cookies.token}`
        }
      })
      .then((response) => {
        setUserName(response.data.name);
        if (response.data.image_url !== null) {
          setIconUrl(response.data.image_url);
        }
      })
      .catch((error) => {
        setErrorMessage(`ユーザー情報の取得に失敗しました。${error}`);
      });
  }, []);

  return (
    <div className="page">
      <Header />
      <h1 className="user_edit_h1">ユーザー情報編集</h1>
      {!userName ? (
        <Loading />
      ) : (
        <main className="update">
          <p className="error-message">{errorMessage}</p>
          <form onSubmit={handleSubmit(updateName)} className="update__form">
            <label>ユーザー名</label>
            <br />
            <input
              type="text"
              {...register('name', { required: true })}
              onChange={handleNameChange}
              className="update__form--name"
              defaultValue={
                userName
              } /*「value={user.name}」だとユーザーの名前を修正できないので「defaultValue」を使う*/
            />
            <p>
              {errors.name?.type === 'required' && (
                <b className="error-message">※アカウント名を入力してください。</b>
              )}
            </p>
            <label>アイコン画像アップロード</label>
            <br />
            <input
              type="file"
              onChange={handleIconUrlChange}
              accept=".jpg, .png"
              className="icon-uploads"
            />
            <div>
              <img src={IconUrl} id="icon" alt="ユーザーのアイコン画像" className="icon_image" />
            </div>
            <br />
            <button type="submit" className="update__form--button">
              更新
            </button>
          </form>
        </main>
      )}
    </div>
  );
};
